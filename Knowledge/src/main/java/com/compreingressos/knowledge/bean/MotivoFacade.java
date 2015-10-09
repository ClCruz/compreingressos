/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.bean;

import com.compreingressos.knowledge.model.Motivo;
import java.util.List;
import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.PersistenceContext;

/**
 *
 * @author edicarlos.barbosa
 */
@Stateless
public class MotivoFacade extends AbstractFacade<Motivo> {
    @PersistenceContext(unitName = "KnowledgePU")
    private EntityManager em;

    @Override
    protected EntityManager getEntityManager() {
        return em;
    }

    public MotivoFacade() {
        super(Motivo.class);
    }

    @Override
    public List<Motivo> findAll() {
        return getEntityManager().createNamedQuery("Motivo.findAll").getResultList();
    }
    
}
