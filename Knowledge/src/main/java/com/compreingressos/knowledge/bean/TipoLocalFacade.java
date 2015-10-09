/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.bean;

import com.compreingressos.knowledge.model.TipoLocal;
import java.util.List;
import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.PersistenceContext;

/**
 *
 * @author edicarlos.barbosa
 */
@Stateless
public class TipoLocalFacade extends AbstractFacade<TipoLocal> {
    @PersistenceContext(unitName = "KnowledgePU")
    private EntityManager em;

    @Override
    protected EntityManager getEntityManager() {
        return em;
    }

    public TipoLocalFacade() {
        super(TipoLocal.class);
    }

    @Override
    public List<TipoLocal> findAll() {
        return getEntityManager().createNamedQuery("TipoLocal.findAll").getResultList();
    }
    
}
